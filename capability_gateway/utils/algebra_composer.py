import rdflib, pyparsing 
from rdflib.plugins.sparql import algebra
from rdflib.plugins.sparql import parser
from rdflib.paths import (
    Path, InvPath, AlternativePath, SequencePath, MulPath, NegatedPath)


groupGraphPatternPredicateTypes = ['BGP', 'Join']

def evalSelectQuery(ctx, part):
	return "SELECT %s" % (evalPart(ctx, part.p))


def evalBGP(ctx, part):
	bindings = []
	for (s, p, o) in part.triples:
		bindings.append('%s %s %s.' % (evalExpr(ctx, s), evalExpr(ctx, p), evalExpr(ctx, o)))

	return "\t%s" % ' '.join(bindings)

def evalProject(ctx, part):

	# list of variables to project in the query
	if part.PV:
		# shorthand if the request variables are all of the in-scope variables
		if (set(part.PV) == part._vars):
			return '*'

		projections = []
		base_part = part.p

		for pv in part.PV:
			extendedProjectVars = None
			extensions = None

			# traverse down the graph to capture all information in the Extend algebra elements
			extensions = traverseExtendForPV(ctx, part.p, pv)
			if (extensions):
				(extendedProjectVars, base_part) = extensions

			# if they exist, then this variable is bound by expression, otherwise its bound by in-scope value
			if (extendedProjectVars):
				projections.append('(%s AS ?%s)' % (extendedProjectVars, pv,))
			else:
				projections.append('?%s' % (pv,))

			extendedProjectVars = None

		# finish handling projections and continue from the next non-Extend/non-Project node in the graph
		return '%s WHERE {%s}' % (' '.join(projections), evalPart(ctx, base_part))


def traverseExtendForPV(ctx, part, pv):
	if part and part.name == 'Extend':
		if part.var == pv:
			return ('%s' % evalExpr(ctx, part.expr), part.p)
		else:
			return traverseExtendForPV(ctx, part.p, pv)


def evalExpr(ctx, part):
	if isinstance(part, rdflib.term.Variable):
		return "?%s" % part

	elif isinstance(part, rdflib.term.Literal):
		return evalLiteral(ctx, part)

	elif isinstance(part, rdflib.term.URIRef):
		return evalURIRef(ctx, part)

	elif isinstance(part, rdflib.plugins.sparql.parserutils.Expr):
		exprParts = []

		if part.expr and not part.name.startswith('Unary'):
			exprParts.append("%s" % (evalExpr(ctx, part.expr)))

		if (part.name == 'ConditionalAndExpression'):
			for p in part.other:
				exprParts.append(" && %s" % (evalExpr(ctx, p)))
		elif (part.name == 'ConditionalOrExpression'):
			for p in part.other:
				exprParts.append(" || %s" % (evalExpr(ctx, p)))

		elif (part.name in ('MultiplicativeExpression', 'AdditiveExpression')):
			for i, op in list(enumerate(part.op)):
				exprParts.append('%s%s' % (part.op[i], evalExpr(ctx, part.other[i])))

		elif (part.name == 'RelationalExpression'):
			exprParts.append(' %s %s' % (part.op, evalExpr(ctx, part.other)))

		elif (part.name in ('UnaryMinus')):
			exprParts.append("-(%s)" % (evalExpr(ctx, part.expr)))
		elif (part.name in ('UnaryPlus')):
			exprParts.append("+(%s)" % (evalExpr(ctx, part.expr)))
		elif (part.name in ('UnaryNot')):
			exprParts.append("!%s" % (evalExpr(ctx, part.expr)))

		elif (part.name.startswith('Builtin_')):
			exprParts.append("%s" % (evalBuiltin(ctx, part)))

		# Bracket complex expressions to be safe
		return '(%s)'% ''.join(exprParts)

	elif (isinstance(part, list)):
		list_elts = []
		for m in part:
			list_elts.append(evalExpr(ctx, m))
		return '(%s)' % (', '.join(list_elts))

	elif (isinstance(part, Path)):
		return '(%s)' % (evalPath(ctx, part))

	else:
		print "Unrecognised Expression:"
		print type(part)
		print dir(part)


def evalPath(ctx, part):
	for i, _ in enumerate(part.args):
		part.args[i] = ctx.namespace_manager.normalizeUri(part.args[i])

	# TODO(ben): add other types of paths (see: rdflib/paths.py)
	if (isinstance(part,AlternativePath)):
		return ' | '.join(part.args)
	else:
		raise Exception('Unknown Path type: ', part)


def evalLiteral(ctx, part):
	if(str(part.datatype).endswith('integer')):
		return str(part)
	else:
		return '"%s"' % part

def evalURIRef(ctx, part):
	return ctx.namespace_manager.normalizeUri(part)

def evalFilter(ctx, part):
	filter_prefix = ''
	if (part.p):
		filter_prefix = evalPart(ctx, part.p)

	return '%s FILTER %s' % (filter_prefix, evalExpr(ctx, part.expr))

def evalSlice(ctx, part):
	slice_restrictions = []
	if (part.start and part.start != 0):
		slice_restrictions.append('OFFSET %s' % part.start)
	if (part.length and part.length > 0):
		slice_restrictions.append('LIMIT %s' % part.length)

	return '%s\n%s' % (evalPart(ctx, part.p), "\n".join(slice_restrictions))

def evalUnion(ctx, part):
	return '%s UNION %s' % (
		'%s' % evalPart(ctx, part.p1) if part.p1.name == 'Union' else '{ %s }' % evalPart(ctx, part.p1), 
		'%s' % evalPart(ctx, part.p2) if part.p2.name == 'Union' else '{ %s }' % evalPart(ctx, part.p2))

def evalLeftJoin(ctx, part):
	if (part.expr.name == 'TrueFilter'):
		return '%s OPTIONAL { %s } ' % ( evalPart(ctx, part.p1), evalPart(ctx, part.p2) )
	else:
		return '%s OPTIONAL { %s FILTER %s } ' % ( evalPart(ctx, part.p1), evalPart(ctx, part.p2), evalExpr(ctx, part.expr) )

def evalJoin(ctx, part):
	# The translated SERVICE algebra sets a graph attribute to specify this is a SERVICE subpattern
	if part.graph:
		return "\n %s \n %s \n" % (evalPart(ctx, part.p1), evalTranslatedServicePattern(ctx, part.p2, part.graph))
	else:
		return "\n %s \n %s \n" % (evalPart(ctx, part.p1), evalPart(ctx, part.p2))

def evalServicePattern(ctx, part):
	return "\tSERVICE <%s> { \n %s \n\t}" % (part.term, evalPart(ctx, part.graph))

# TODO: support SILENT' 
def evalTranslatedServicePattern(ctx, part, graph):
	return "\tSERVICE <%s> { \n \t%s \n\t}" % (graph, evalPart(ctx, part))


def evalSubSelect(ctx, part):
	if (part.projection):
		projection_vars = []
		for p in part.projection:
			projection_vars.append(evalExpr(ctx, p.var))
		return "		SELECT %s WHERE { %s }" % (' '.join(projection_vars), evalPart(ctx, part.where))
	else:
		return "		SELECT * WHERE { %s }" % (evalPart(ctx, part.where))


def evalDistinct(ctx, part):
	return 'DISTINCT %s' % (evalPart(ctx, part.p))

def evalPart(ctx, part):

	if part.name == 'SelectQuery':
		return evalSelectQuery(ctx, part)
	elif part.name == 'Project':
		return evalProject(ctx, part)
	elif part.name in ('BGP', 'TriplesBlock'):
		return evalBGP(ctx, part)
	elif part.name == 'Filter':
		return evalFilter(ctx, part)
	elif part.name == 'Slice':
		return evalSlice(ctx, part)
	elif part.name == 'Distinct':
		return evalDistinct(ctx, part)
	elif part.name == 'Union':
		return evalUnion(ctx, part)
	elif part.name == 'Join':
		return evalJoin(ctx, part)
	elif part.name == 'ServiceGraphPattern':
		return evalServicePattern(ctx, part)
	elif part.name == 'GroupGraphPatternSub':
		return ' '.join([evalPart(ctx, p) for p in part.part])
	elif part.name == 'OptionalGraphPattern':
		return 'OPTIONAL { %s }' % ' '.join([evalPart(ctx, p) for p in part.graph.part])
	elif part.name == 'LeftJoin':
		return evalLeftJoin(ctx, part)

	elif part.name == 'SubSelect':
		return evalSubSelect(ctx, part)

	elif part.name.startswith('Builtin_'):
		return evalBuiltin(ctx, part)
	else:
	    raise Exception('I dont know: %s' % part.name)

def evalBuiltin(ctx, part):
	if part.name == 'Builtin_REGEX':
		if (part.flags):
			return ' REGEX(%s, "%s", "%s") ' % (evalExpr(ctx, part.text), part.pattern, part.flags)
		else:
			return ' REGEX(%s, "%s") ' % (evalExpr(ctx, part.text), part.pattern)

	elif part.name == 'Builtin_BOUND':
		return ' BOUND(%s)' % evalExpr(ctx, part.arg)